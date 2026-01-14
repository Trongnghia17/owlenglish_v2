import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { getSectionFilters, getFilters, getCollections } from '../api/exams.api';
import './OnlineExamLibrary.css';
import muiten from "../../../assets/images/u_len.svg";
import trangdau from "../../../assets/images/u_arrow-left.svg";
import trangcuoi from "../../../assets/images/u_arrow-right.svg";
import Loading from '../../../components/common/Loading';
import WritingIcon from '@/assets/images/exam-writing.png';
import ListeningIcon from '@/assets/images/exam-listening.png';
import ReadingIcon from '@/assets/images/exam-reading.png';
import SpeakingIcon from '@/assets/images/exam-speaking.png';
import Line_Graph from '@/assets/images/Line_Graph.svg';
import Bar_Chart from '@/assets/images/Bar_Chart.svg';
import Pie_Chart from '@/assets/images/Pie_Chart.svg';
import Table from '@/assets/images/Table.svg';
import Mixed_Graph from '@/assets/images/Mixed_Graph.svg';
import Map from '@/assets/images/Map.svg';
import Process from '@/assets/images/Process.svg';
import img_default from '@/assets/images/img_default.png';

export default function OnlineExamLibrary() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [skills, setSkills] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedTask, setSelectedTask] = useState('all');
  const [sortBy, setSortBy] = useState('newest');
  const [filters, setFilters] = useState({
    skillType: [],
    difficulty: [],
  });
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
  });
  const [collections, setCollections] = useState([]);
  const LEVEL_META = {
    easy: {
      label: 'Dễ',
      color: '#35A815',
    },
    medium: {
      label: 'Trung bình',
      color: '#F0A931',
    },
    hard: {
      label: 'Khó',
      color: '#F05052',
    },
  };
  const { examType } = useParams();
  const [filtersSidebar, setFiltersSidebar] = useState([]);
  const [collectionIds, setCollectionIds] = useState([]);
  const [selectedLevel, setSelectedLevel] = useState('all');
  const [selectedFilterIds, setSelectedFilterIds] = useState([]);
  const onSelectFilter = (filterId) => {
    setSelectedFilterIds(prev =>
      prev.includes(filterId)
        ? prev.filter(id => id !== filterId)
        : [...prev, filterId]
    );
  };
  const SKILL_META = {
    writing: {
      icon: WritingIcon,
      colorClass: 'sidebar-item__toggle--writing',
      itemIcons: {
        'Line Graph': Line_Graph,
        'Bar Chart': Bar_Chart,
        'Pie Chart': Pie_Chart,
        'Table': Table,
        'Mixed Graph': Mixed_Graph,
        'Map': Map,
        'Process': Process,
      },
    },
    listening: {
      icon: ListeningIcon,
      colorClass: 'sidebar-item__toggle--listening',
    },
    reading: {
      icon: ReadingIcon,
      colorClass: 'sidebar-item__toggle--reading',
    },
    speaking: {
      icon: SpeakingIcon,
      colorClass: 'sidebar-item__toggle--speaking',
    },
  };

  const [expandedGroups, setExpandedGroups] = useState({});

  const toggleGroup = (key) => {
    setExpandedGroups(prev => ({
      ...prev,
      [key]: !prev[key],
    }));
  };

  useEffect(() => {
    fetchSkills(1);
  }, [
    examType,
    collectionIds,
    selectedLevel,
    searchQuery,
    selectedFilterIds,
    sortBy
  ]);

  useEffect(() => {
    fetchFilters();
  }, [examType]);

  const fetchFilters = async () => {
    try {
      const res = await getFilters({ type: examType });
      if (res.data.success) {
        setFiltersSidebar(res.data.data);
      }
    } catch (e) {
      console.error(e);
    }
  };

  const fetchCollections = async () => {
    try {
      const res = await getCollections({ type: examType });
      if (res.data.success) {
        setCollections(res.data.data);
      }
    } catch (e) {
      console.error(e);
    }
  };

  useEffect(() => {
    fetchCollections();
  }, [examType]);


  const fetchSkills = async (page = 1) => {
    if (!examType) return;

    try {
      setLoading(true);

      const params = {
        type: examType,
        page,
        sort: sortBy,
      };

      if (collectionIds.length > 0) {
        params.collectionIds = collectionIds;
      }

      if (selectedLevel !== 'all') {
        params.level = selectedLevel;
      }

      if (searchQuery) {
        params.search = searchQuery;
      }

      if (selectedFilterIds.length > 0) {
        params.filters = selectedFilterIds;
      }

      const response = await getSectionFilters(params);

      if (response.data.success) {
        // const skillsData = response.data.data.data || response.data.data;
        const paginated = response.data.data;
        setSkills(paginated.data);
        setPagination({
          current_page: paginated.current_page,
          last_page: paginated.last_page,
        });

      }
    } catch (error) {
      console.error('Error fetching skills:', error);
    } finally {
      setLoading(false);
    }
  };


  const handleFilterChange = (filterType, value) => {
    setFilters(prev => ({
      ...prev,
      [filterType]: prev[filterType].includes(value)
        ? prev[filterType].filter(v => v !== value)
        : [...prev[filterType], value]
    }));
  };
  const handleTakeExam = (examId) => {
    navigate(`/bo-de/${examType}/${examId}`);
  };

  const getSkillImage = (skill) => {
    if (skill.image) {
      const image = skill.image;
      if (image.startsWith('http')) {
        return image;
      }
      return `${import.meta.env.VITE_API_BASE_URL}/storage/${image}`;
    }

    const colors = {
      reading: '3b82f6',
      writing: '10b981',
      listening: 'f59e0b',
      speaking: 'ef4444',
    };
    const color = colors[skill.skill_type] || '6366f1';
    return `https://via.placeholder.com/280x180/${color}/ffffff?text=${skill.skill_type || 'Skill'}`;
  };



  return (
    <div className="online-exam-library online-exam-library--exam-package">
      {/* Header (giữ nguyên) */}
      <div className="online-exam-library__header-wrapper">
        {/* Breadcrumb (giữ nguyên) */}
        <div className="online-exam-library__breadcrumb">
          <span className="online-exam-library__breadcrumb-item" onClick={() => navigate('/')}>Trang chủ</span>
          <span className="online-exam-library__breadcrumb-separator"><svg xmlns="http://www.w3.org/2000/svg" width="16"
            height="16" viewBox="0 0 16 16" fill="none">
            <path
              d="M10.3602 7.52685L6.58682 3.76019C6.52484 3.6977 6.45111 3.64811 6.36987 3.61426C6.28863 3.58041 6.20149 3.56299 6.11348 3.56299C6.02548 3.56299 5.93834 3.58041 5.8571 3.61426C5.77586 3.64811 5.70213 3.6977 5.64015 3.76019C5.51598 3.8851 5.44629 4.05406 5.44629 4.23019C5.44629 4.40631 5.51598 4.57528 5.64015 4.70019L8.94015 8.03352L5.64015 11.3335C5.51598 11.4584 5.44629 11.6274 5.44629 11.8035C5.44629 11.9796 5.51598 12.1486 5.64015 12.2735C5.70189 12.3365 5.77552 12.3866 5.85677 12.421C5.93802 12.4553 6.02528 12.4732 6.11348 12.4735C6.20169 12.4732 6.28894 12.4553 6.37019 12.421C6.45144 12.3866 6.52507 12.3365 6.58682 12.2735L10.3602 8.50685C10.4278 8.44443 10.4818 8.36866 10.5188 8.28433C10.5557 8.19999 10.5748 8.10892 10.5748 8.01685C10.5748 7.92479 10.5557 7.83372 10.5188 7.74938C10.4818 7.66505 10.4278 7.58928 10.3602 7.52685Z"
              fill="#6D6D6D" />
          </svg></span>
          <span className="online-exam-library__breadcrumb-item online-exam-library__breadcrumb-item--active">Đề thi <span className="text-up-text">{examType}</span></span>
        </div>

        <div className="online-exam-library__header-top">
          <h1 className="online-exam-library__title">Đề thi <span className="text-up-text">{examType}</span></h1>
          <div className="online-exam-library__header-controls">
            <div className="online-exam-library__search">
              <svg
                className="online-exam-library__search-icon"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
              <input
                type="text"
                className="online-exam-library__search-input"
                placeholder="Tìm kiếm"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
            <div className="exam-library__filter-collection online-exam-library__sort-wrapper">
              <select className='online-exam-library__sort-select'
                value={collectionIds[0] || 'all'}
                onChange={(e) => {
                  const value = e.target.value;
                  setCollectionIds(value === 'all' ? [] : [Number(value)]);
                }}
              >
                <option value="all">Đề thi</option>
                {collections.map(col => (
                  <option key={col.id} value={col.id}>
                    {col.name}
                  </option>
                ))}
              </select>
              <svg className="online-exam-library__sort-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M9.40845 12.9499L4.70011 8.23328C4.622 8.15581 4.56001 8.06364 4.5177 7.96209C4.4754 7.86054 4.45361 7.75162 4.45361 7.64161C4.45361 7.5316 4.4754 7.42268 4.5177 7.32113C4.56001 7.21958 4.622 7.12741 4.70011 7.04994C4.85625 6.89474 5.06746 6.80762 5.28761 6.80762C5.50777 6.80762 5.71898 6.89474 5.87511 7.04994L10.0418 11.1749L14.1668 7.04994C14.3229 6.89474 14.5341 6.80762 14.7543 6.80762C14.9744 6.80762 15.1856 6.89474 15.3418 7.04994C15.4205 7.12712 15.4832 7.21916 15.5261 7.32072C15.569 7.42229 15.5913 7.53136 15.5918 7.64161C15.5913 7.75187 15.569 7.86094 15.5261 7.9625C15.4832 8.06406 15.4205 8.1561 15.3418 8.23328L10.6334 12.9499C10.5554 13.0345 10.4607 13.102 10.3553 13.1482C10.2499 13.1944 10.136 13.2182 10.0209 13.2182C9.90586 13.2182 9.79202 13.1944 9.68661 13.1482C9.58119 13.102 9.48648 13.0345 9.40845 12.9499Z" fill="#6D6D6D" />
              </svg>
            </div>

            <div className="exam-library__filter-level online-exam-library__sort-wrapper">
              <select
                className='online-exam-library__sort-select'
                value={selectedLevel}
                onChange={(e) => setSelectedLevel(e.target.value)}
              >
                <option value="all">Mức độ</option>
                <option value="easy">Dễ</option>
                <option value="medium">Vừa</option>
                <option value="hard">Khó</option>
              </select>
              <svg className="online-exam-library__sort-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M9.40845 12.9499L4.70011 8.23328C4.622 8.15581 4.56001 8.06364 4.5177 7.96209C4.4754 7.86054 4.45361 7.75162 4.45361 7.64161C4.45361 7.5316 4.4754 7.42268 4.5177 7.32113C4.56001 7.21958 4.622 7.12741 4.70011 7.04994C4.85625 6.89474 5.06746 6.80762 5.28761 6.80762C5.50777 6.80762 5.71898 6.89474 5.87511 7.04994L10.0418 11.1749L14.1668 7.04994C14.3229 6.89474 14.5341 6.80762 14.7543 6.80762C14.9744 6.80762 15.1856 6.89474 15.3418 7.04994C15.4205 7.12712 15.4832 7.21916 15.5261 7.32072C15.569 7.42229 15.5913 7.53136 15.5918 7.64161C15.5913 7.75187 15.569 7.86094 15.5261 7.9625C15.4832 8.06406 15.4205 8.1561 15.3418 8.23328L10.6334 12.9499C10.5554 13.0345 10.4607 13.102 10.3553 13.1482C10.2499 13.1944 10.136 13.2182 10.0209 13.2182C9.90586 13.2182 9.79202 13.1944 9.68661 13.1482C9.58119 13.102 9.48648 13.0345 9.40845 12.9499Z" fill="#6D6D6D" />
              </svg>
            </div>
            <div className="online-exam-library__sort">
              <div className="online-exam-library__sort-wrapper">
                <select
                  className="online-exam-library__sort-select"
                  value={sortBy}
                  onChange={(e) => setSortBy(e.target.value)}
                >
                  <option value="newest">Mới nhất</option>
                  <option value="oldest">Cũ nhất</option>
                  <option value="name">Tên A-Z</option>
                </select>
                <svg className="online-exam-library__sort-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M9.40845 12.9499L4.70011 8.23328C4.622 8.15581 4.56001 8.06364 4.5177 7.96209C4.4754 7.86054 4.45361 7.75162 4.45361 7.64161C4.45361 7.5316 4.4754 7.42268 4.5177 7.32113C4.56001 7.21958 4.622 7.12741 4.70011 7.04994C4.85625 6.89474 5.06746 6.80762 5.28761 6.80762C5.50777 6.80762 5.71898 6.89474 5.87511 7.04994L10.0418 11.1749L14.1668 7.04994C14.3229 6.89474 14.5341 6.80762 14.7543 6.80762C14.9744 6.80762 15.1856 6.89474 15.3418 7.04994C15.4205 7.12712 15.4832 7.21916 15.5261 7.32072C15.569 7.42229 15.5913 7.53136 15.5918 7.64161C15.5913 7.75187 15.569 7.86094 15.5261 7.9625C15.4832 8.06406 15.4205 8.1561 15.3418 8.23328L10.6334 12.9499C10.5554 13.0345 10.4607 13.102 10.3553 13.1482C10.2499 13.1944 10.136 13.2182 10.0209 13.2182C9.90586 13.2182 9.79202 13.1944 9.68661 13.1482C9.58119 13.102 9.48648 13.0345 9.40845 12.9499Z" fill="#6D6D6D" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="online-exam-library__content">
        {/* Main Content */}
        <main className="online-exam-library__main">
          <div className='exam-package-container online-exam-library__content'>
            <aside className="exam-sidebar">
              {filtersSidebar.map(skill => {
                const meta = SKILL_META[skill.slug];

                return (
                  <div key={skill.id} className="exam-skill">

                    {/* ===== Skill header ===== */}
                    <div className="exam-skill-header">
                      <img src={meta.icon} alt={skill.name} />
                      <span>{skill.name}</span>
                    </div>

                    {/* ===== Groups ===== */}
                    {skill.children.map(group => {
                      const key = `${skill.id}-${group.id}`;
                      const open = expandedGroups[key];

                      return (
                        <div key={group.id} className={`exam-group ${meta.colorClass}`}>

                          {/* Group header */}
                          <button
                            className={`exam-group-header ${open ? 'open' : ''}`}
                            onClick={() => toggleGroup(key)}
                          >
                            <span>{group.name}</span>
                            <img
                              src={muiten}
                              alt="muiten"
                              className={`arrow ${open ? 'up' : 'down'}`}
                            />
                          </button>

                          {/* Group items */}
                          {open && (
                            <div className="exam-group-content">
                              {group.children.map(item => (
                                <div
                                  key={item.id}
                                  className={`exam-group-item ${selectedFilterIds.includes(item.id) ? 'active' : ''
                                    }`}
                                  onClick={() => onSelectFilter(item.id)}
                                >
                                  <span className="item-title">
                                    {skill.slug === 'writing' &&
                                      meta.itemIcons?.[item.name] && (
                                        <img
                                          src={meta.itemIcons[item.name]}
                                          alt={item.name}
                                          className="item-con-icon"
                                        />
                                      )}

                                    {item.name}
                                  </span>
                                </div>

                              ))}
                            </div>
                          )}
                        </div>
                      );
                    })}
                  </div>
                );
              })}
            </aside>

            <div className='online-exam-library__right'>
              <div className="online-exam-library__grid">
                {loading ? (
                  <div className="online-exam-library__loading">
                    <Loading />
                  </div>
                ) : skills.length === 0 ? (
                  <div className="online-exam-library__empty">
                    Không có bài thi nào
                  </div>
                ) : (
                  skills.map(skill => (
                    <div
                      key={skill.id}
                      className="online-exam-library__card"
                    >
                      <img
                        src={getSkillImage(skill)}
                        alt={skill.title}
                        className="online-exam-library__card-image"
                        onError={(e) => {
                          e.currentTarget.src = img_default;
                        }}
                      />
                      <div className="online-exam-library__card-content">
                        <h3 className="online-exam-library__card-title">{skill.title}</h3>
                        <div className="online-exam-library__card-subtitle">
                          <strong>Tiến trình:</strong> <span> <span className="text-up-text">{examType} {skill.skill?.skill_type} </span></span>
                        </div>
                        {skill.level && (
                          <div
                            className="exam-level-badge"
                          >
                            <span className='level-mucdo'>Mức độ: </span>  <span className='level-label' style={{ color: LEVEL_META[skill.level]?.color }}>{LEVEL_META[skill.level]?.label}</span>
                          </div>
                        )}
                        <div className="online-exam-library__card-footer">
                          <button
                            className="online-exam-library__card-button"
                            onClick={(e) => {
                              e.stopPropagation();
                              handleTakeExam(skill.id);
                            }}
                          >
                            Thi ngay
                            <span className="online-exam-library__card-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10" fill="none">
                              <path d="M9.93333 4.68674C9.89367 4.58445 9.8342 4.491 9.75833 4.41174L5.59167 0.245076C5.51397 0.167378 5.42173 0.105744 5.32021 0.0636935C5.21869 0.0216433 5.10988 0 5 0C4.77808 0 4.56525 0.0881567 4.40833 0.245076C4.33063 0.322775 4.269 0.415017 4.22695 0.516535C4.1849 0.618054 4.16326 0.72686 4.16326 0.836743C4.16326 1.05866 4.25141 1.27149 4.40833 1.42841L7.15833 4.17008H0.833333C0.61232 4.17008 0.400358 4.25787 0.244078 4.41415C0.0877975 4.57044 0 4.7824 0 5.00341C0 5.22442 0.0877975 5.43639 0.244078 5.59267C0.400358 5.74895 0.61232 5.83674 0.833333 5.83674H7.15833L4.40833 8.57841C4.33023 8.65588 4.26823 8.74805 4.22592 8.8496C4.18362 8.95115 4.16183 9.06007 4.16183 9.17008C4.16183 9.28009 4.18362 9.38901 4.22592 9.49056C4.26823 9.59211 4.33023 9.68427 4.40833 9.76174C4.4858 9.83985 4.57797 9.90185 4.67952 9.94415C4.78107 9.98646 4.88999 10.0082 5 10.0082C5.11001 10.0082 5.21893 9.98646 5.32048 9.94415C5.42203 9.90185 5.5142 9.83985 5.59167 9.76174L9.75833 5.59508C9.8342 5.51582 9.89367 5.42237 9.93333 5.32008C10.0167 5.11719 10.0167 4.88963 9.93333 4.68674Z" fill="#045CCE" />
                            </svg></span>
                          </button>
                        </div>
                      </div>
                    </div>
                  ))
                )}
              </div>

              <div className="exam-collection-library__pagination-wrapper">
                {pagination.last_page > 1 && (
                  <div className="exam-pagination">
                    {/* Prev */}
                    <button
                      className="page-btn page-btn--prev"
                      disabled={pagination.current_page === 1}
                      onClick={() => fetchSkills(1)}
                    >
                      <img src={trangdau} alt="" />  <span>Trang đầu</span>
                    </button>

                    <div className="page-numbers">
                      {Array.from({ length: pagination.last_page }, (_, i) => i + 1)
                        .filter(page =>
                          page === 1 ||
                          page === pagination.last_page ||
                          Math.abs(page - pagination.current_page) <= 1
                        )
                        .map((page, index, arr) => (
                          <>
                            {index > 0 && page - arr[index - 1] > 1 && (
                              <span className="page-dots">...</span>
                            )}

                            <button
                              key={page}
                              className={`page-btn ${page === pagination.current_page ? 'active' : ''}`}
                              onClick={() => fetchSkills(page)}
                            >
                              {page}
                            </button>
                          </>
                        ))}
                    </div>

                    {/* Next */}
                    <button
                      className="page-btn page-btn--next"
                      disabled={pagination.current_page === pagination.last_page}
                      onClick={() => fetchSkills(pagination.last_page)}
                    >
                      <span>Trang cuối</span> <img src={trangcuoi} alt="" />
                    </button>
                  </div>
                )}

              </div>
            </div>

          </div>
        </main>
      </div>
    </div>
  );
}
